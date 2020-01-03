import $ from 'jquery'

if (typeof Object.assign === 'undefined') {
  Object.assign = $.extend
}
export default class {
  constructor(options = {}) {
    this.$el = options.$el ? options.$el : $(document)
    this.el = this.$el[0]
    delete options.$el

    this.options = options

    this.isProcessed = false
  }

  initialize() {
    // Initialize the Component
  }
  process() {
    // Bind the Event on the Component
  }

  run(...state) {
    // run Component
    if (!this.isProcessed) {
      this.initialize()
      this.process()
    }

    this.isProcessed = true
  }

  triggerResize() {
    if (document.createEvent) {
      const ev = document.createEvent('Event')
      ev.initEvent('resize', true, true)
      window.dispatchEvent(ev)
    } else {
      element = document.documentElement
      const event = document.createEventObject()
      element.fireEvent('onresize', event)
    }
  }
}
