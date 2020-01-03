import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'slidePanel'

class SlidePanel extends Plugin {
  getName() {
    return NAME
  }
  static getDefaults() {
    return {
      closeSelector: '.slidePanel-close',
      mouseDragHandler: '.slidePanel-handler',
      loading: {
        template(options) {
          return `<div class="${options.classes.loading}">
                    <div class="loader loader-default"></div>
                  </div>`
        },
        showCallback(options) {
          this.$el.addClass(`${options.classes.loading}-show`)
        },
        hideCallback(options) {
          this.$el.removeClass(`${options.classes.loading}-show`)
        }
      }
    }
  }
  render() {
    if (typeof $.slidePanel === 'undefined') {
      return
    }
    if (!this.options.url) {
      this.options.url = this.$el.attr('href')
      this.options.url =
        this.options.url && this.options.url.replace(/.*(?=#[^\s]*$)/, '')
    }

    this.$el.data('slidePanelWrapAPI', this)
  }
  show() {
    const options = this.options

    $.slidePanel.show({
      url: options.url
    },
    options
    )
  }
  static api() {
    return 'click|show'
  }
}

Plugin.register(NAME, SlidePanel)
export default SlidePanel
