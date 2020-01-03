import Plugin from 'Plugin'

const NAME = 'summernote'

class Summernote extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      height: 300
    }
  }
}

Plugin.register(NAME, Summernote)

export default Summernote
