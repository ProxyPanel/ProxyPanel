import Plugin from 'Plugin'
import * as Config from 'Config'

const NAME = 'switchery'

class SwitcheryPlugin extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      color: Config.colors('primary', 600)
    }
  }

  render() {
    if (typeof Switchery === 'undefined') {
      return
    }
    new Switchery(this.$el[0], this.options)
  }
}

Plugin.register(NAME, SwitcheryPlugin)

export default SwitcheryPlugin
