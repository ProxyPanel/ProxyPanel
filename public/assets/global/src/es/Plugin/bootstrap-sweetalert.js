import Plugin from 'Plugin'

const NAME = 'sweetalert'

class Sweetalert extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$el.data('sweetalertWrapApi', this)
  }

  show() {
    if (typeof swal === 'undefined') {
      return
    }

    swal(this.options)
  }

  static api() {
    return 'click|show'
  }
}

Plugin.register(NAME, Sweetalert)

export default Sweetalert
