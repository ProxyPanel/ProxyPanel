import gutil from 'gulp-util';
import config from '../../config';
import notifier from 'node-notifier';

export default function(error) {
  // Send error to notification center with gulp-notify
  if (config.enable.notify) {
    notifier.notify({
      title: config.notify.title,
      subtitle: 'Failure!',
      message: error.message,
    });
  }

  gutil.log(gutil.colors.red(error));
  // Keep gulp from hanging on this task
  this.emit('end');
}
