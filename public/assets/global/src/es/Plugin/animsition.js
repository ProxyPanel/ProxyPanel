import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'animsition'

class Animsition extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      inClass: 'fade-in',
      outClass: 'fade-out',
      inDuration: 800,
      outDuration: 500,
      linkElement: '.animsition-link',
      loading: true,
      loadingParentElement: 'body',
      loadingClass: 'loader',
      loadingType: 'default',
      timeout: false,
      timeoutCountdown: 5000,
      onLoadEvent: true,
      browser: ['animation-duration', '-webkit-animation-duration'],
      overlay: false,
      // random: true,
      overlayClass: 'animsition-overlay-slide',
      overlayParentElement: 'body',

      inDefaults: [
        'fade-in',
        'fade-in-up-sm',
        'fade-in-up',
        'fade-in-up-lg',
        'fade-in-down-sm',
        'fade-in-down',
        'fade-in-down-lg',
        'fade-in-left-sm',
        'fade-in-left',
        'fade-in-left-lg',
        'fade-in-right-sm',
        'fade-in-right',
        'fade-in-right-lg',
        // 'overlay-slide-in-top', 'overlay-slide-in-bottom', 'overlay-slide-in-left', 'overlay-slide-in-right',
        'zoom-in-sm',
        'zoom-in',
        'zoom-in-lg'
      ],
      outDefaults: [
        'fade-out',
        'fade-out-up-sm',
        'fade-out-up',
        'fade-out-up-lg',
        'fade-out-down-sm',
        'fade-out-down',
        'fade-out-down-lg',
        'fade-out-left-sm',
        'fade-out-left',
        'fade-out-left-lg',
        'fade-out-right-sm',
        'fade-out-right',
        'fade-out-right-lg',
        // 'overlay-slide-out-top', 'overlay-slide-out-bottom', 'overlay-slide-out-left', 'overlay-slide-out-right'
        'zoom-out-sm',
        'zoom-out',
        'zoom-out-lg'
      ]
    }
  }

  render(callback) {
    const options = this.options

    if (options.random) {
      const li = options.inDefaults.length

      const lo = options.outDefaults.length

      const ni = parseInt(li * Math.random(), 0)

      const no = parseInt(lo * Math.random(), 0)

      options.inClass = options.inDefaults[ni]
      options.outClass = options.outDefaults[no]
    }

    this.$el.animsition(options)

    $(`.${options.loadingClass}`).addClass(`loader-${options.loadingType}`)

    if (this.$el.animsition('supportCheck', options)) {
      if ($.isFunction(callback)) {
        this.$el.one('animsition.end', () => {
          callback.call()
        })
      }

      return true
    }
    if ($.isFunction(callback)) {
      callback.call()
    }
    return false
  }
}

Plugin.register(NAME, Animsition)

export default Animsition
