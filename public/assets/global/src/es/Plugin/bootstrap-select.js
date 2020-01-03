import Plugin from 'Plugin'

const NAME = 'selectpicker'

class Selectpicker extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      style: 'btn-select',
      iconBase: 'icon',
      tickIcon: 'wb-check'
    }
  }
}

Plugin.register(NAME, Selectpicker)

export default Selectpicker
