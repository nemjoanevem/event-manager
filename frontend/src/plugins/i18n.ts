import { createI18n } from "vue-i18n"
import hu from "@/locales/hu.json"
import en from "@/locales/en.json"


const savedLang = localStorage.getItem("app_lang") || "en";

const i18n = createI18n({
    legacy: false,
    locale: savedLang,
    fallbackLocale: "en",
    messages: {
        hu,
        en,
    } as any,
})

export default i18n
