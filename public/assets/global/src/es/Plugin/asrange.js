import Plugin from 'Plugin'

const NAME = 'asRange'

class AsRange extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      tip: false,
      scale: false
    }
  }
}

Plugin.register(NAME, AsRange)

export default AsRange
