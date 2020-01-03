import Plugin from 'Plugin'

const NAME = 'tokenfield'

class Tokenfield extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, Tokenfield)

export default Tokenfield
