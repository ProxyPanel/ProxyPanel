import Plugin from 'Plugin'

const NAME = 'owlCarousel'

class OwlCarousel extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      // autoWidth: true,
      loop: true,
      nav: true,
      dots: false,
      dotsClass: 'owl-dots owl-dots-fall',
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 3
        }
      }
    }
  }
}

Plugin.register(NAME, OwlCarousel)

export default OwlCarousel
