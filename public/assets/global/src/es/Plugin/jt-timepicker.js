import Plugin from 'Plugin'

const NAME = 'timepicker'

class Timepicker extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }
}

Plugin.register(NAME, Timepicker)

export default Timepicker
