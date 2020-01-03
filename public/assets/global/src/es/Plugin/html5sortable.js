import Plugin from 'Plugin'

const NAME = 'sortable'

class Sortable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      connectWith: false,
      placeholder: null,
      // dragImage can be null or a Element
      dragImage: null,
      disableIEFix: false,
      placeholderClass: 'sortable-placeholder',
      draggingClass: 'sortable-dragging',
      hoverClass: false
    }
  }

  render() {
    const $el = this.$el

    sortable(this.$el.get(0), this.options)
  }
}

Plugin.register(NAME, Sortable)

export default Sortable
