import Plugin from 'Plugin'

const NAME = 'asSpinner'

class AsSpinner extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      namespace: 'spinnerUi',
      skin: null,
      min: '-10',
      max: 100,
      mousewheel: true
    }
  }
}

Plugin.register(NAME, AsSpinner)

export default AsSpinner
