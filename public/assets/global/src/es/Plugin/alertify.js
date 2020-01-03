import Plugin from 'Plugin'

const NAME = 'alertify'

class Alertify extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      type: 'alert',
      delay: 5000,
      theme: 'bootstrap'
    }
  }

  render() {
    if (this.options.labelOk) {
      this.options.okBtn = this.options.labelOk
    }

    if (this.options.labelCancel) {
      this.options.cancelBtn = this.options.labelCancel
    }

    this.$el.data('alertifyWrapApi', this)
  }

  show() {
    if (typeof alertify === 'undefined') {
      return
    }
    const options = this.options
    if (typeof options.delay !== 'undefined') {
      alertify.delay(options.delay)
    }

    if (typeof options.theme !== 'undefined') {
      alertify.theme(options.theme)
    }

    if (typeof options.cancelBtn !== 'undefined') {
      alertify.cancelBtn(options.cancelBtn)
    }

    if (typeof options.okBtn !== 'undefined') {
      alertify.okBtn(options.okBtn)
    }

    if (typeof options.placeholder !== 'undefined') {
      alertify.delay(options.placeholder)
    }

    if (typeof options.defaultValue !== 'undefined') {
      alertify.delay(options.defaultValue)
    }

    if (typeof options.maxLogItems !== 'undefined') {
      alertify.delay(options.maxLogItems)
    }

    if (typeof options.closeLogOnClick !== 'undefined') {
      alertify.delay(options.closeLogOnClick)
    }

    switch (options.type) {
      case 'confirm':
        alertify.confirm(
          options.confirmTitle,
          () => {
            alertify.success(options.successMessage)
          },
          () => {
            alertify.error(options.errorMessage)
          }
        )
        break
      case 'prompt':
        alertify.prompt(
          options.promptTitle,
          (str, ev) => {
            const message = options.successMessage.replace('%s', str)
            alertify.success(message)
          },
          (ev) => {
            alertify.error(options.errorMessage)
          }
        )
        break
      case 'log':
        alertify.log(options.logMessage)
        break
      case 'success':
        alertify.success(options.successMessage)
        break
      case 'error':
        alertify.error(options.errorMessage)
        break
      default:
        alertify.alert(options.alertMessage)
        break
    }
  }

  static api() {
    return 'click|show'
  }
}

Plugin.register(NAME, Alertify)

export default Alertify
