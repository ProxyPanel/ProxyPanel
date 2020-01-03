import Plugin from 'Plugin'

const NAME = 'ladda'

class LaddaPlugin extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      type: 'normal',
      timeout: 2000
    }
  }

  render() {
    if (typeof Ladda === 'undefined') {
      return
    }

    if (this.options.type === 'progress') {
      this.options.callback = function (instance) {
        let progress = 0
        const interval = setInterval(() => {
          progress = Math.min(progress + Math.random() * 0.1, 1)
          instance.setProgress(progress)

          if (progress === 1) {
            instance.stop()
            clearInterval(interval)
          }
        }, 200)
      }
    }
    Ladda.bind(this.$el[0], this.options)
  }
}

Plugin.register(NAME, LaddaPlugin)

export default LaddaPlugin
