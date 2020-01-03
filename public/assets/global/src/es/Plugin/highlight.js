import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'highlight'

class Highlight extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    hljs.initHighlightingOnLoad()
  }
}

Plugin.register(NAME, Highlight)

export default Highlight
