import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'tablesaw'

class Tablesaw extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  static api() {
    return () => {
      if (typeof $.fn.tablesaw === 'undefined') {
        return
      }

      $(document).trigger('enhance.tablesaw')
    }
  }
}

Plugin.register(NAME, Tablesaw)

export default Tablesaw
