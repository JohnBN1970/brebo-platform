(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboProjects = {
    attach(context) {
      once('brebo-projects', '.brebo-project', context).forEach((project) => {
        project.classList.add('is-ready');
      });
    }
  };
})(Drupal, once);
