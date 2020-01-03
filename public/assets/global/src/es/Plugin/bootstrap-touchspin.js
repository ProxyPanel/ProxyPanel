import Plugin from 'Plugin'

const NAME = 'TouchSpin'

class TouchSpin extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      // verticalupclass: 'wb-plus',
      // verticaldownclass: 'wb-minus',
      buttondown_class: 'btn btn-outline btn-default',
      buttonup_class: 'btn btn-outline btn-default'
    }
  }
}

Plugin.register(NAME, TouchSpin)

export default TouchSpin
