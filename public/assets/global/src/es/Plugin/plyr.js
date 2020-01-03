import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'plyr'

$(document).ready(() => {
  const a = new XMLHttpRequest()

  const d = document

  const u = 'https://cdn.plyr.io/1.1.5/sprite.svg'
  const b = d.body

  // Check for CORS support
  if ('withCredentials' in a) {
    a.open('GET', u, true)
    a.send()
    a.onload = () => {
      const c = d.createElement('div')
      c.style.display = 'none'
      c.innerHTML = a.responseText
      b.insertBefore(c, b.childNodes[0])
    }
  }
})

class Plyr extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (typeof plyr === 'undefined') {
      return
    }
    plyr.setup(this.$el[0], this.options)
  }
}

Plugin.register(NAME, Plyr)

export default Plyr
