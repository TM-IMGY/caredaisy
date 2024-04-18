export default class SearchScope {
    constructor() {
        let today = new Date();
        let oneYearAgo = new Date();
        oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
        this.OneYearAgo =
            oneYearAgo.getFullYear() +
            "-" +
            ("0" + (oneYearAgo.getMonth() + 1)).slice(-2);
        this.Today =
            today.getFullYear() +
            "-" +
            ("0" + (today.getMonth() + 1)).slice(-2);
    }
}
