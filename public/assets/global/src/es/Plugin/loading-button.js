import Plugin from 'Plugin'

const NAME = 'loadingButton'

class LoadingButton extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.text = this.$el.text()
    this.$el.data('loadingButtonApi', this)
  }

  loading() {
    const $el = this.$el

    let i = this.options.time

    const loadingText = this.options.loadingText

    const opacity = this.options.opacity
    const text = this.text
    $el.text(`${loadingText}(${i})`).css('opacity', opacity)

    const timeout = setInterval(() => {
      $el.text(`${loadingText}(${--i})`)
      if (i === 0) {
        clearInterval(timeout)
        $el.text(text).css('opacity', '1')
      }
    }, 1000)
  }

  static api() {
    return 'click|loading'
  }

  static getDefaults() {
    return {
      loadingText: 'Loading',
      time: 20,
      opacity: '0.6'
    }
  }
}

Plugin.register(NAME, LoadingButton)

export default LoadingButton
