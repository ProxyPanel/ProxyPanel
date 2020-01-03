import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'panel'

function getPanelAPI($el) {
  if ($el.length <= 0) {
    return
  }
  let api = $el.data('panelAPI')

  if (api) {
    return api
  }

  api = new Panel($el, $.extend(true, {}, Panel.getDefaults(), $el.data()))
  api.render()
  return api
}

class Panel extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  static api() {
    return () => {
      $(document).on(
        'click.site.panel',
        '[data-toggle="panel-fullscreen"]',
        function (e) {
          e.preventDefault()
          const api = getPanelAPI($(this).closest('.panel'))
          api.toggleFullscreen()
        }
      )

      $(document).on(
        'click.site.panel',
        '[data-toggle="panel-collapse"]',
        function (e) {
          e.preventDefault()
          const api = getPanelAPI($(this).closest('.panel'))
          api.toggleContent()
        }
      )

      $(document).on(
        'click.site.panel',
        '[data-toggle="panel-close"]',
        function (e) {
          e.preventDefault()
          const api = getPanelAPI($(this).closest('.panel'))
          api.close()
        }
      )

      $(document).on(
        'click.site.panel',
        '[data-toggle="panel-refresh"]',
        function (e) {
          e.preventDefault()
          const api = getPanelAPI($(this).closest('.panel'))
          api.load()
        }
      )
    }
  }

  render(context) {
    const $el = this.$el

    this.isFullscreen = false
    this.isClose = false
    this.isCollapse = false
    this.isLoading = false

    this.$panelBody = $el.find('.panel-body')
    this.$fullscreen = $el.find('[data-toggle="panel-fullscreen"]')
    this.$collapse = $el.find('[data-toggle="panel-collapse"]')
    this.$loading = null
    if ($el.hasClass('is-collapse')) {
      this.isCollapse = true
    }

    if (typeof this.options.loadCallback === 'string') {
      this.options.loadCallback = window[this.options.loadCallback]
    }

    $el.data('panelAPI', this)
  }

  load(callback) {
    const $el = this.$el

    let type = $el.data('loader-type')
    if (!type) {
      type = 'default'
    }

    callback = callback || this.options.loadCallback

    this.$loading = $(`<div class="panel-loading">
                          <div class="loader loader-${type}"></div>
                        </div>`).appendTo($el)
    $el.addClass('is-loading')
    $el.trigger('loading.uikit.panel')
    this.isLoading = true

    if (typeof callback === 'function') {
      callback.call(this)
    }
  }

  done() {
    if (this.isLoading === true) {
      this.$loading.remove()
      this.$el.removeClass('is-loading')
      this.$el.trigger('loading.done.uikit.panel')
    }
  }

  toggleContent() {
    if (this.isCollapse) {
      this.showContent()
    } else {
      this.hideContent()
    }
  }

  showContent() {
    if (this.isCollapse !== false) {
      this.$el.removeClass('is-collapse')

      if (this.$collapse.hasClass('wb-plus')) {
        this.$collapse.removeClass('wb-plus').addClass('wb-minus')
      }

      this.$el.trigger('shown.uikit.panel')

      this.isCollapse = false
    }
  }

  hideContent() {
    if (this.isCollapse !== true) {
      this.$el.addClass('is-collapse')

      if (this.$collapse.hasClass('wb-minus')) {
        this.$collapse.removeClass('wb-minus').addClass('wb-plus')
      }

      this.$el.trigger('hidden.uikit.panel')
      this.isCollapse = true
    }
  }

  toggleFullscreen() {
    if (this.isFullscreen) {
      this.leaveFullscreen()
    } else {
      this.enterFullscreen()
    }
  }

  enterFullscreen() {
    if (this.isFullscreen !== true) {
      this.$el.addClass('is-fullscreen')

      if (this.$fullscreen.hasClass('wb-expand')) {
        this.$fullscreen.removeClass('wb-expand').addClass('wb-contract')
      }

      this.$el.trigger('enter.fullscreen.uikit.panel')
      this.isFullscreen = true
    }
  }

  leaveFullscreen() {
    if (this.isFullscreen !== false) {
      this.$el.removeClass('is-fullscreen')

      if (this.$fullscreen.hasClass('wb-contract')) {
        this.$fullscreen.removeClass('wb-contract').addClass('wb-expand')
      }

      this.$el.trigger('leave.fullscreen.uikit.panel')
      this.isFullscreen = false
    }
  }

  toggle() {
    if (this.isClose) {
      this.open()
    } else {
      this.close()
    }
  }

  open() {
    if (this.isClose !== false) {
      this.$el.removeClass('is-close')
      this.$el.trigger('open.uikit.panel')

      this.isClose = false
    }
  }

  close() {
    if (this.isClose !== true) {
      this.$el.addClass('is-close')
      this.$el.trigger('close.uikit.panel')

      this.isClose = true
    }
  }
}

Plugin.register(NAME, Panel)

export default Panel
