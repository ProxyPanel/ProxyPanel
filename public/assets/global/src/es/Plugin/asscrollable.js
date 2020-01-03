import Plugin from 'Plugin'

const NAME = 'scrollable'

class Scrollable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      namespace: 'scrollable',
      contentSelector: '> [data-role=\'content\']',
      containerSelector: '> [data-role=\'container\']'
    }
  }

  render() {
    const $el = this.$el

    $el.asScrollable(this.options)
  }
}

Plugin.register(NAME, Scrollable)

export default Scrollable
