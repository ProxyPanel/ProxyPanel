import $ from 'jquery'
import Plugin from 'Plugin'
import * as Config from 'Config'

class PeityBar extends Plugin {
  getName() {
    return 'peityBar'
  }

  static getDefaults() {
    return {
      delimiter: ',',
      fill: [Config.colors('primary', 400)],
      height: 22,
      max: null,
      min: 0,
      padding: 0.1,
      width: 44
    }
  }

  render() {
    if (!$.fn.peity) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (options.skin) {
      const skinColors = Config.colors(options.skin)
      if (skinColors) {
        options.fill = [skinColors[400]]
      }
    }

    $el.peity('bar', options)
  }
}

Plugin.register('peityBar', PeityBar)

class PeityDonut extends Plugin {
  getName() {
    return 'peityDonut'
  }

  static getDefaults() {
    return {
      delimiter: null,
      fill: [
        Config.colors('primary', 700),
        Config.colors('primary', 400),
        Config.colors('primary', 200)
      ],
      height: null,
      innerRadius: null,
      radius: 11,
      width: null
    }
  }

  render() {
    if (!$.fn.peity) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (options.skin) {
      const skinColors = Config.colors(options.skin)
      if (skinColors) {
        options.fill = [skinColors[700], skinColors[400], skinColors[200]]
      }
    }

    $el.peity('donut', options)
  }
}
Plugin.register('peityDonut', PeityDonut)

class PeityLine extends Plugin {
  getName() {
    return 'peityLine'
  }

  static getDefaults() {
    return {
      delimiter: ',',
      fill: [Config.colors('primary', 200)],
      height: 22,
      max: null,
      min: 0,
      stroke: Config.colors('primary', 600),
      strokeWidth: 1,
      width: 44
    }
  }

  render() {
    if (!$.fn.peity) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (options.skin) {
      const skinColors = Config.colors(options.skin)
      if (skinColors) {
        options.fill = [skinColors[200]]
        options.stroke = skinColors[600]
      }
    }

    $el.peity('line', options)
  }
}
Plugin.register('peityLine', PeityLine)

class PeityPie extends Plugin {
  getName() {
    return 'peityPie'
  }

  static getDefaults() {
    return {
      delimiter: null,
      fill: [
        Config.colors('primary', 700),
        Config.colors('primary', 400),
        Config.colors('primary', 200)
      ],
      height: null,
      radius: 11,
      width: null
    }
  }

  render() {
    if (!$.fn.peity) {
      return
    }

    const $el = this.$el

    const options = this.options

    if (options.skin) {
      const skinColors = Config.colors(options.skin)
      if (skinColors) {
        options.fill = [skinColors[700], skinColors[400], skinColors[200]]
      }
    }

    $el.peity('pie', options)
  }
}

Plugin.register('peityPie', PeityPie)

export {
  PeityBar,
  PeityLine,
  PeityDonut,
  PeityPie
}
