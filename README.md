# My Dictionary

**Smooth WordPress Plugin for Multilingual Sites**

A lightweight and flexible WordPress plugin that lets you add a customizable dictionary/glossary feature to your site — designed for multilingual support, easy integration, and smooth user experience.

---

## 🚀 Features

* 📘 **Multilingual dictionary support** — handle words and definitions in multiple languages.
* ⚙️ **Smooth front-end UI** — display dictionary entries elegantly on your site.
* 🧩 **Easy WordPress integration** — works with any theme.
* 🗂️ **Admin interface for managing entries** — add/edit/delete words and definitions.

---

## 📦 Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/eduvallve/my-dictionary.git
   ```

2. **Upload to WordPress**
   Move the `my-dictionary` folder to your WordPress plugins directory:

   ```plaintext
   /wp-content/plugins/
   ```

3. **Activate the plugin**
   Go to your WordPress admin dashboard → **Plugins** → **My Dictionary** → *Activate*.

4. **Configure settings**
   You’ll find a new **My Dictionary** admin menu where you can add/manage all dictionary entries.

---

## 🛠 Usage

### Manage Dictionary Entries

1. Go to **My Dictionary** start page and select your **primary language**. This is the language you use to create your pages, posts, etc. in the first place. Below, there are all types of entries in your website. Select which ones need translations. Save those changes.

2.  Go to the **entries tab**. You will see all your entries types. In each type, you will see a list with all corresponding entries. Go to one entry and select **start translation**. After that, the entry will become translatable.

3. **Click on the selected entry.** You will find all plain text strings contained into the entry, and the possibility to write correspondences for each in other languages. You will see a progress bar t as well, which gives information about que translated percentage.

4. **Apply translations.** If a string has no translation, then it will be shown in the default language. Save changes.

5. **Add a language switcher.** Add the `[my-dictionary-menu-language-switcher]` shortcode anywhere to enable a language switcher. This shortcode can be added as many times as needed.

6. **Check functionality.** Apply some translations and navigate to the proper entry URL. Then change the language by means of the language switcher.

7. **You've done!** Congratulations! 🎉

---

## 📁 Plugin Structure

Here’s how the repository is organized:

```
my-dictionary/
├── admin/            # Admin Dashboard screens & settings
├── functions/        # Core functions and logic
├── ui/               # Front-end templates, blocks, styles
├── my-dictionary.php # Main plugin bootstrap file
├── languages.json    # Supported language data
├── README.md         # This file
```

---

## 🤝 Contributing

Want to contribute? Great!

1. Fork the project
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add awesome feature'`)
4. Push to branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

Please follow standard WordPress coding conventions.

---

## 📝 License

This project is open-source and available under the **MIT License**.

---

## 💡 Support

For issues, suggestions, or help with integration, please open a GitHub issue or reach out via the repository’s discussion board.
