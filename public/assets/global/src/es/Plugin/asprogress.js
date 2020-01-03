import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'progress'

class Progress extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      bootstrap: true,

      onUpdate(n) {
        const per = (n - this.min) / (this.max - this.min)
        if (per < 0.5) {
          this.$target
            .addClass('progress-bar-success')
            .removeClass('progress-bar-warning progress-bar-danger')
        } else if (per >= 0.5 && per < 0.8) {
          this.$target
            .addClass('progress-bar-warning')
            .removeClass('progress-bar-success progress-bar-danger')
        } else {
          this.$target
            .addClass('progress-bar-danger')
            .removeClass('progress-bar-success progress-bar-warning')
        }
      },

      labelCallback(n) {
        let label
        const labelType = this.$element.data('labeltype')

        if (labelType === 'percentage') {
          const percentage = this.getPercentage(n)
          label = `${percentage}%`
        } else if (labelType === 'steps') {
          let total = this.$element.data('totalsteps')
          if (!total) {
            total = 10
          }
          const step = Math.round(total * (n - this.min) / (this.max - this.min))
          label = `${step} / ${total}`
        } else {
          label = n
        }

        if (this.$element.parent().hasClass('contextual-progress')) {
          this.$element
            .parent()
            .find('.progress-label')
            .html(label)
        }

        return label
      }
    }
  }

  render() {
    if (!$.fn.asProgress) {
      return
    }

    const $el = this.$el

    $el.asProgress(this.options)
  }
}

Plugin.register(NAME, Progress)

export default Progress
