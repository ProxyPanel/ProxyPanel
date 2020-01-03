import Plugin from 'Plugin'
import * as Config from 'Config'

const NAME = 'gmaps'

class GmapsPlugin extends Plugin {
  getName() {
    return NAME
  }

  render() {}

  static getDefaults() {
    return {
      styles: [{
        featureType: 'landscape',
        elementType: 'all',
        stylers: [{
          color: '#ffffff'
        }]
      }, {
        featureType: 'poi',
        elementType: 'all',
        stylers: [{
          color: '#ffffff'
        }]
      }, {
        featureType: 'road',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '700')
        }]
      }, {
        featureType: 'administrative',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '500')
        }]
      }, {
        featureType: 'road.highway',
        elementType: 'geometry.fill',
        stylers: [{
          color: Config.colors('blue-grey', '300')
        }]
      }, {
        featureType: 'road.arterial',
        elementType: 'geometry.fill',
        stylers: [{
          color: '#e0e0e0'
        }]
      }, {
        featureType: 'water',
        elementType: 'geometry.fill',
        stylers: [{
          color: Config.colors('blue-grey', '200')
        }]
      }, {
        featureType: 'water',
        elementType: 'labels.text.fill',
        stylers: [{
          color: '#000000'
        }]
      }, {
        featureType: 'poi',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '500')
        }]
      }, {
        featureType: 'road',
        elementType: 'labels.text.stroke',
        stylers: [{
          visibility: 'off'
        }]
      }, {
        featureType: 'poi.attraction',
        elementType: 'labels.text.stroke',
        stylers: [{
          visibility: 'off'
        }]
      }, {
        featureType: 'poi',
        elementType: 'labels.text.stroke',
        stylers: [{
          visibility: 'off'
        }]
      }, {
        featureType: 'road.local',
        elementType: 'all',
        stylers: [{
          color: Config.colors('blue-grey', '200')
        }, {
          weight: 0.5
        }]
      }, {
        featureType: 'road.arterial',
        elementType: 'geometry',
        stylers: [{
          color: Config.colors('blue-grey', '300')
        }]
      }, {
        featureType: 'road.arterial',
        elementType: 'geometry.stroke',
        stylers: [{
          visibility: 'off'
        }]
      }, {
        featureType: 'road.highway',
        elementType: 'geometry.stroke',
        stylers: [{
          visibility: 'off'
        }, {
          color: '#000000'
        }]
      }, {
        featureType: 'poi',
        elementType: 'all',
        stylers: [{
          visibility: 'off'
        }, {
          color: '#000000'
        }]
      }, {
        featureType: 'poi',
        elementType: 'labels.text',
        stylers: [{
          visibility: 'on'
        }, {
          color: Config.colors('blue-grey', '700')
        }]
      }, {
        featureType: 'road.local',
        elementType: 'labels.text',
        stylers: [{
          color: Config.colors('blue-grey', '700')
        }]
      }, {
        featureType: 'transit',
        elementType: 'all',
        stylers: [{
          color: Config.colors('blue-grey', '100')
        }]
      }, {
        featureType: 'transit.station',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '500')
        }]
      }, {
        featureType: 'road',
        elementType: 'labels.text.stroke',
        stylers: [{
          visibility: 'off'
        }]
      }, {
        featureType: 'road',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '600')
        }]
      }, {
        featureType: 'administrative.neighborhood',
        elementType: 'labels.text',
        stylers: [{
          color: Config.colors('blue-grey', '700')
        }]
      }, {
        featureType: 'poi',
        elementType: 'labels.text.stroke',
        stylers: [{
          color: '#ffffff'
        }]
      }, {
        featureType: 'road.highway',
        elementType: 'labels.icon',
        stylers: [{
          visibility: 'on'
        }, {
          hue: '#ffffff'
        }, {
          saturation: -100
        }, {
          lightness: 50
        }]
      }, {
        featureType: 'water',
        elementType: 'labels.text.stroke',
        stylers: [{
          visibility: 'on'
        }, {
          color: '#ffffff'
        }]
      }, {
        featureType: 'administrative.neighborhood',
        elementType: 'labels.text.stroke',
        stylers: [{
          color: '#ffffff'
        }]
      }, {
        featureType: 'administrative',
        elementType: 'labels.text.stroke',
        stylers: [{
          color: '#ffffff'
        }]
      }, {
        featureType: 'water',
        elementType: 'labels.text.fill',
        stylers: [{
          color: Config.colors('blue-grey', '600')
        }]
      }]
    }
  }
}

Plugin.register(NAME, GmapsPlugin)

export default GmapsPlugin
