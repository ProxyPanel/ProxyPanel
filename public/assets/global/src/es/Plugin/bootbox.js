import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'bootbox'

class Bootbox extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$el.data('bootboxWrapApi', this)
  }

  show() {
    if (typeof bootbox === 'undefined') {
      return
    }

    const options = this.options

    if (options.classname) {
      options.className = options.classname
    }

    if (options.className) {
      options.className += ' modal-simple'
    }

    if (
      typeof options.callback === 'string' &&
      $.isFunction(window[options.callback])
    ) {
      options.callback = window[options.callback]
    }

    if (options.type) {
      switch (options.type) {
        case 'alert':
          bootbox.alert(options)
          break
        case 'confirm':
          bootbox.confirm(options)
          break
        case 'prompt':
          bootbox.prompt(options)
          break
        default:
          bootbox.dialog(options)
      }
    } else {
      bootbox.dialog(options)
    }
  }

  static getDefaults() {
    return {
      message: '',
      className: 'modal-simple'
    }
  }

  static api() {
    return 'click|show'
  }
}
Plugin.register(NAME, Bootbox)

export default Bootbox
