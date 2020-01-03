import Plugin from 'Plugin'

const NAME = 'sortable'

class Sortable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      ghostClass: 'sortable-placeholder',
      dragClass: 'sortable-dragging'
    }
  }

  render() {
    const $el = this.$el

    window.Sortable.create(this.$el.get(0), this.options)
  }
}

Plugin.register(NAME, Sortable)

export default Sortable
