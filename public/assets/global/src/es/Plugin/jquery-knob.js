import Plugin from 'Plugin'

const NAME = 'knob'

class Knob extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      min: -50,
      max: 50,
      width: 120,
      height: 120,
      thickness: '.1'
    }
  }
}

Plugin.register(NAME, Knob)

export default Knob
