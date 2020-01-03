import Plugin from 'Plugin'

const NAME = 'select2'

class Select2 extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      width: 'style'
    }
  }
}

Plugin.register(NAME, Select2)

export default Select2
