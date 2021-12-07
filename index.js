panel.plugin('tillprochaska/localizations', {
  sections: {
    localizations: {

      data: () => ({
        add: false,
        localizations: [],
      }),

      computed: {
        items() {
          return this.localizations.map(localization => ({
            text: localization.name,
            info: localization.code,
            link: localization.link,
            flag: {
              status: localization.status,
              click: () => this.$dialog(localization.link + '/changeStatus'),
            },
            data: {
              'data-is-origin': localization.isOrigin,
              'data-is-current': localization.isCurrent,
            },
          }));
        },
      },

      created() {
        this.fetchData();
        this.$events.$on('page.changeStatus', () => this.fetchData());
      },

      methods: {
        create() {
          this.$dialog(this.link + '/localizations/create');
        },

        fetchData() {
          this.load().then(response => {
            this.add = response.add;
            this.link = response.link;
            this.localizations = response.localizations;
          });
        }
      },

      template: `
        <section class="k-localizations-section">
          <header class="k-section-header">
            <k-headline>Localizations</k-headline>
            <k-button-group
              v-if="add"
              :buttons="[{ text: $t('create'), icon: 'add', click: create }]"
            />
          </header>
          <k-collection :items="items" />
        </section>
      `,

    }
  }
});
