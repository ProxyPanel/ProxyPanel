import Plugin from 'Plugin'

const NAME = 'tagsinput'

class Tagsinput extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      tagClass: 'badge badge-default'
    }
  }
}

Plugin.register(NAME, Tagsinput)

export default Tagsinput
