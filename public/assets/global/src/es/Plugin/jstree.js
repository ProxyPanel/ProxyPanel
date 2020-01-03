import Plugin from 'Plugin'

const NAME = 'jstree'

class Jstree extends Plugin {
  getName() {
    return NAME
  }
}

Plugin.register(NAME, Jstree)

export default Jstree
