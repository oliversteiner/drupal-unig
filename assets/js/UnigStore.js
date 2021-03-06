(function($, Drupal, drupalSettings) {
  Drupal.behaviors.unigStore = {
    attach(context, settings) {
      // onload

      const peopleList = drupalSettings.unig.project.keywords;
      const keywordsList = drupalSettings.unig.project.people;
      this.terms = peopleList.concat(keywordsList);
    },

    name: 'unig',
    module: 'none',
    items: [],
    terms: [],

    init(name) {
      this.module = name;
      const host = Drupal.behaviors.unigData.project.host;
      const projectId = Drupal.behaviors.unigData.project.id;
      this.name = host + '.unig.' + name + '.' + projectId;

      this.load();
    },

    add(id) {
      id = parseInt(id, 10);
      this.items.push(id);
      this.save();
    },

    toggle(id) {
      let status = false;
      if (this.find(id)) {
        status = true;
        this.remove(id);
      } else {
        status = false;
        this.add(id);
      }
      this.save();
      return status;
    },

    /**
     * remove from list
     *
     */
    remove(id) {
      const index = this.items.indexOf(id);
      if (index > -1) {
        this.items.splice(index, 1);
        this.save();
      }
    },

    clear() {
      this.items = [];
      this.save();
    },

    /**
     * save to localStorage
     */
    save() {
      localStorage.setItem(this.name, this.items);
    },

    /**
     * load from localStorage
     */
    load() {
      const storeContent = localStorage.getItem(this.name);
      let items = [];

      if (storeContent != null) {
        const storeArray = storeContent.split(',');
        items = Drupal.behaviors.unig.cleanArray(storeArray);
      }

      this.items = items;
      return items;
    },

    /**
     * returns array or false
     */
    get() {
      return this.items;
    },

    getNameByID(id) {
      const term = this.terms.find(term => term.id === id);
      return term.name;
    },

    count() {
      return this.items.length;
    },

    find(id) {
      id = parseInt(id, 10);

      const search = this.items.indexOf(id);

      return search !== -1;
    },
  };
})(jQuery, Drupal, drupalSettings);
