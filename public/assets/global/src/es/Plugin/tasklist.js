import Plugin from 'Plugin'

const NAME = 'tasklist'

class TaskList extends Plugin {
  getName() {
    return NAME
  }

  render() {
    this.$el.data('tasklistApi', this)
    this.$checkbox = this.$el.find('[type="checkbox"]')
    this.$el.trigger('change.site.task')
  }
  toggle() {
    if (this.$checkbox.is(':checked')) {
      this.$el.addClass('task-done')
    } else {
      this.$el.removeClass('task-done')
    }
  }
  static api() {
    return 'change|toggle'
  }
}
Plugin.register(NAME, TaskList)

export default TaskList
