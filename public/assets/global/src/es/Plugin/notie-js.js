import Plugin from 'Plugin'

const NAME = 'notie'

class Notie extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$el.data('notieApi', this)
  }

  show() {
    const options = this.options

    if (options.type !== undefined) {
      switch (options.type) {
        case 'confirm':
          notie.confirm(Object.assign(options, {
            submitCallback() {
              if (options.submitCallback && typeof window[options.submitCallback] === 'function') {
                window[options.submitCallback]()
              } else {
                notie.alert({
                  type: 1,
                  text: options.submitMsg,
                  time: 1.5
                })
              }
            },
            cancelCallback() {
              if (options.cancelCallback && typeof window[options.cancelCallback] === 'function') {
                window[options.cancelCallback]()
              } else {
                notie.alert({
                  type: 3,
                  text: options.cancelMsg,
                  time: 1.5
                })
              }
            }
          }))
          break
        case 'input':
          notie.input(Object.assign(options, {
            submitCallback(value) {
              if (options.submitCallback && typeof window[options.submitCallback] === 'function') {
                window[options.submitCallback](value)
              } else {
                notie.alert({
                  type: 1,
                  text: `you entered: ${value}`,
                  time: 1.5
                })
              }
            },
            cancelCallback(value) {
              if (options.cancelCallback && typeof window[options.cancelCallback] === 'function') {
                window[options.cancelCallback](value)
              } else {
                notie.alert({
                  type: 1,
                  text: `You cancelled with this value: ${value}`,
                  time: 1.5
                })
              }
            }
          }))
          break
        case 'select':
          notie.select(options)
          break
        case 'date':
          notie.date(options)
          break
        default:
          notie.alert(options)
      }
    }
  }

  static api() {
    return 'click|show'
  }
}

Plugin.register(NAME, Notie)

export default Notie
