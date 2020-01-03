import Plugin from 'Plugin'

const NAME = 'datepair'

class Datepair extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      startClass: 'datepair-start',
      endClass: 'datepair-end',
      timeClass: 'datepair-time',
      dateClass: 'datepair-date'
    }
  }
}

Plugin.register(NAME, Datepair)

export default Datepair
