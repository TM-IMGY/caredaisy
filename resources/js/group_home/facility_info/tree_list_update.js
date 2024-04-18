export default class TreeListUpdate{
    constructor(){
        this.refresher = [];

        document.getElementById('updatabtn_corporation').addEventListener('click',this.refresh.bind(this));
        document.getElementById('updatabtn_office').addEventListener('click',this.refresh.bind(this));
        document.getElementById('updatabtn_institution').addEventListener('click',this.refresh.bind(this));
    }

    refreshTree(callBack)
    {
        this.refresher.push(callBack);
    }

    async refresh()
    {
        await new Promise(resolve => setTimeout(resolve, 500));
        this.refresher.forEach(callBack=>callBack());
    }
}