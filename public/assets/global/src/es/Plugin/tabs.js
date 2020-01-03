import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'tabs'

class Tabs extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (
      this.$el.find('.nav-tabs-horizontal') &&
      $.fn.responsiveHorizontalTabs
    ) {
      this.type = 'horizontal'
      this.$el.responsiveHorizontalTabs()
    } else if (this.$el.find('.nav-tabs-vertical')) {
      this.type = 'vertical'
      this.$el.children().matchHeight()
    }
  }
}

Plugin.register(NAME, Tabs)

export default Tabs
