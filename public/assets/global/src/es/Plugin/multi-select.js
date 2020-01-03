import Plugin from 'Plugin'

const NAME = 'multiSelect'

class MultiSelect extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, MultiSelect)

export default MultiSelect
