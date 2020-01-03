import Plugin from 'Plugin'

const NAME = 'clockpicker'

class Clockpicker extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      donetext: 'Done'
    }
  }
}

Plugin.register(NAME, Clockpicker)

export default Clockpicker
