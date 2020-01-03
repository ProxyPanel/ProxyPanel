import Plugin from 'Plugin'

const NAME = 'ionRangeSlider'

class IonRangeSlider extends Plugin {
  getName() {
    return NAME
  }
}

Plugin.register(NAME, IonRangeSlider)

export default IonRangeSlider
