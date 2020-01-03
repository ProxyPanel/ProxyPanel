import Plugin from 'Plugin'

const NAME = 'asColorPicker'

class AsColorPicker extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      namespace: 'colorInputUi'
    }
  }
}

Plugin.register(NAME, AsColorPicker)

export default AsColorPicker
