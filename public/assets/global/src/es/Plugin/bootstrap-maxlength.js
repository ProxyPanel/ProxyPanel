import Plugin from 'Plugin'

const NAME = 'maxlength'

class Maxlength extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      warningClass: 'badge badge-warning',
      limitReachedClass: 'badge badge-danger'
    }
  }
}

Plugin.register(NAME, Maxlength)

export default Maxlength
