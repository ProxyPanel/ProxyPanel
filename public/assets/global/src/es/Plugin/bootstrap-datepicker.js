import Plugin from 'Plugin'

const NAME = 'datepicker'

class Datepicker extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      autoclose: true
    }
  }
}

Plugin.register(NAME, Datepicker)

export default Datepicker
