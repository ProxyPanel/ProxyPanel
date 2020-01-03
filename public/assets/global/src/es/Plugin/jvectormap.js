import Plugin from 'Plugin'
import * as Config from 'Config'

const NAME = 'vectorMap'

class VectorMap extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      map: 'world_mill',
      backgroundColor: '#fff',
      zoomAnimate: true,
      regionStyle: {
        initial: {
          fill: Config.colors('primary', 600)
        },
        hover: {
          fill: Config.colors('primary', 500)
        },
        selected: {
          fill: Config.colors('primary', 800)
        },
        selectedHover: {
          fill: Config.colors('primary', 500)
        }
      },
      markerStyle: {
        initial: {
          r: 8,
          fill: Config.colors('red', 600),
          'stroke-width': 0
        },
        hover: {
          r: 12,
          stroke: Config.colors('red', 600),
          'stroke-width': 0
        }
      }
    }
  }
}

Plugin.register(NAME, VectorMap)

export default VectorMap
