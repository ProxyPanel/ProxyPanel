import Plugin from 'Plugin'

const NAME = 'nestable'

class Nestable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, Nestable)

export default Nestable
