import Plugin from 'Plugin'

const NAME = 'dropify'

class Dropify extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, Dropify)

export default Dropify
