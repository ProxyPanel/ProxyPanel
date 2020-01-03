import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'gridstack'

class Gridstack extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      cellHeight: 80,
      verticalMargin: 20
    }
  }

  render() {
    if (!$.fn.gridstack) {
      return
    }

    const $el = this.$el

    $el.gridstack(this.options)
  }
}

Plugin.register(NAME, Gridstack)

export default Gridstack
