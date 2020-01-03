import Plugin from 'Plugin'

const NAME = 'labelauty'

class Labelauty extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      same_width: true
    }
  }
}

Plugin.register(NAME, Labelauty)

export default Labelauty
