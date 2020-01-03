import Plugin from 'Plugin'

const NAME = 'magnificPopup'

class MagnificPopup extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      type: 'image',
      closeOnContentClick: true,
      image: {
        verticalFit: true
      }
    }
  }
}

Plugin.register(NAME, MagnificPopup)

export default MagnificPopup
