import Plugin from 'Plugin'

const NAME = 'nprogress'

class Nprogress extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      minimum: 0.15,
      trickleRate: 0.07,
      trickleSpeed: 360,
      showSpinner: false,
      template: '<div class="bar" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
    }
  }

  render() {
    if (typeof NProgress === 'undefined') {
      return
    }

    NProgress.configure(this.options)
  }
}

Plugin.register(NAME, Nprogress)

export default Nprogress
