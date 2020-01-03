import Plugin from 'Plugin'

const NAME = 'iCheck'

class ICheck extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, ICheck)

export default ICheck
