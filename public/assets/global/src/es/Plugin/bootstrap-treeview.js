import $ from 'jquery'
import Plugin from 'Plugin'
import * as Config from 'Config'

const NAME = 'treeview'

class Treeview extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      injectStyle: false,
      expandIcon: 'icon wb-plus',
      collapseIcon: 'icon wb-minus',
      emptyIcon: 'icon',
      nodeIcon: 'icon wb-folder',
      showBorder: false,
      // color: undefined, // "#000000",
      // backColor: undefined, // "#FFFFFF",
      borderColor: Config.colors('blue-grey', 200),
      onhoverColor: Config.colors('blue-grey', 100),
      selectedColor: '#ffffff',
      selectedBackColor: Config.colors('primary', 600),

      searchResultColor: Config.colors('primary', 600),
      searchResultBackColor: '#ffffff'
    }
  }
  render() {
    if (!$.fn.treeview) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (
      typeof options.source === 'string' &&
      $.isFunction(window[options.source])
    ) {
      options.data = window[options.source]()
      delete options.source
    } else if ($.isFunction(options.souce)) {
      options.data = options.source()
      delete options.source
    }

    $el.treeview(options)
  }
}

Plugin.register(NAME, Treeview)

export default Treeview
