import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'paginator'

class Paginator extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      namespace: 'pagination',
      currentPage: 1,
      itemsPerPage: 10,
      disabledClass: 'disabled',
      activeClass: 'active',

      visibleNum: {
        0: 3,
        480: 5
      },

      tpl() {
        return '{{prev}}{{lists}}{{next}}'
      },

      components: {
        prev: {
          tpl() {
            return `<li class="${this
              .namespace}-prev page-item"><a class="page-link" href="javascript:void(0)" aria-label="Prev"><span class="icon wb-chevron-left-mini"></span></a></li>`
          }
        },
        next: {
          tpl() {
            return `<li class="${this
              .namespace}-next page-item"><a class="page-link" href="javascript:void(0)" aria-label="Next"><span class="icon wb-chevron-right-mini"></span></a></li>`
          }
        },
        lists: {
          tpl() {
            let lists = ''

            let remainder =
              this.currentPage >= this.visible
                ? this.currentPage % this.visible
                : this.currentPage
            remainder = remainder === 0 ? this.visible : remainder
            for (let k = 1; k < remainder; k++) {
              lists += `<li class="${this
                .namespace}-items page-item" data-value="${this.currentPage -
                remainder +
                k}"><a class="page-link" href="javascript:void(0)">${this
                .currentPage -
                remainder +
                k}</a></li>`
            }
            lists += `<li class="${this.namespace}-items page-item ${this
              .classes.active}" data-value="${this
              .currentPage}"><a class="page-link" href="javascript:void(0)">${this
              .currentPage}</a></li>`
            for (
              let i = this.currentPage + 1,
                limit =
                i + this.visible - remainder - 1 > this.totalPages
                  ? this.totalPages
                  : i + this.visible - remainder - 1; i <= limit; i++
            ) {
              lists += `<li class="${this
                .namespace}-items page-item" data-value="${i}"><a class="page-link" href="javascript:void(0)">${i}</a></li>`
            }

            return lists
          }
        }
      }
    }
  }

  render() {
    if (!$.fn.asPaginator) {
      return
    }

    const $el = this.$el

    const total = $el.data('total')

    $el.asPaginator(total, this.options)
  }
}

Plugin.register(NAME, Paginator)

export default Paginator
