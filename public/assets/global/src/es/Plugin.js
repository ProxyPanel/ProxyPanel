import $ from 'jquery'

const plugins = {}
const apis = {}

export default class Plugin {
  constructor($el, options = {}) {
    this.name = this.getName()
    this.$el = $el
    this.options = options
    this.isRendered = false
  }

  getName() {
    return 'plugin'
  }

  render() {
    if ($.fn[this.name]) {
      this.$el[this.name](this.options)
    } else {
      return false
    }
  }

  initialize() {
    if (this.isRendered) {
      return false
    }
    this.render()
    this.isRendered = true
  }

  static getDefaults() {
    return {}
  }

  static register(name, obj) {
    if (typeof obj === 'undefined') {
      return
    }

    plugins[name] = obj

    if (typeof obj.api !== 'undefined') {
      Plugin.registerApi(name, obj)
    }
  }

  static registerApi(name, obj) {
    const api = obj.api()

    if (typeof api === 'string') {
      const api = obj.api().split('|')
      const event = `${api[0]}.plugin.${name}`
      const func = api[1] || 'render'

      const callback = function (e) {
        const $el = $(this)
        let plugin = $el.data('pluginInstance')

        if (!plugin) {
          plugin = new obj(
            $el,
            $.extend(true, {}, getDefaults(name), $el.data())
          )
          plugin.initialize()
          $el.data('pluginInstance', plugin)
        }

        plugin[func](e)
      }

      apis[name] = function (selector, context) {
        if (context) {
          $(context).off(event)
          $(context).on(event, selector, callback)
        } else {
          $(selector).on(event, callback)
        }
      }
    } else if (typeof api === 'function') {
      apis[name] = api
    }
  }
}

function getPluginAPI(name) {
  if (typeof name === 'undefined') {
    return apis
  }
  return apis[name]
}

function getPlugin(name) {
  if (typeof plugins[name] !== 'undefined') {
    return plugins[name]
  }
  console.warn(`Plugin:${name} has no warpped class.`)
  return false
}

function getDefaults(name) {
  const PluginClass = getPlugin(name)

  if (PluginClass) {
    return PluginClass.getDefaults()
  }
  return {}
}

function pluginFactory(name, $el, options = {}) {
  const PluginClass = getPlugin(name)

  if (PluginClass && typeof PluginClass.api === 'undefined') {
    return new PluginClass($el, $.extend(true, {}, getDefaults(name), options))
  } else if ($.fn[name]) {
    const plugin = new Plugin($el, options)
    plugin.getName = function () {
      return name
    }
    plugin.name = name
    return plugin
  } else if (typeof PluginClass.api !== 'undefined') {
    // console.log('Plugin:' + name + ' use api render.');
    return false
  }
  console.warn(`Plugin:${name} script is not loaded.`)
  return false
}

export {
  Plugin,
  getPluginAPI,
  getPlugin,
  getDefaults,
  pluginFactory
}
