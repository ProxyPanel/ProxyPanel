import Plugin from 'Plugin'

const NAME = 'webuiPopover'

class WebuiPopover extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      trigger: 'click',
      width: 320,
      multi: true,
      cloaseable: false,
      style: '',
      delay: 300,
      padding: true
    }
  }
}

Plugin.register(NAME, WebuiPopover)

export default WebuiPopover
