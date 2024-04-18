export default class CustomAjax{
    /**
     * GET
     * @param {string} url
     * @param {Object} headers
     * @returns {Promise}
     */
    static async get(url, headers){
        try {
            let response = await fetch(url,{
                headers:{...headers, 'X-Requested-With':'XMLHttpRequest'}, // request is ajax
                method:'GET'
            });

            if(response.redirected){
                location.href = location.href;
            }

            if(response.status == 419){
                location.href = location.href;
            }

            if (!response.ok) {
                const json = await response.json();
                throw new Error(json.message);
            }

            return response;
        } catch (error) {
            throw error;
        } 
    }

    static async send(method, url, headers, data = [], callback = false, callbackFrom = false){
        if (callback == false) {
            callback = (function(){});
        }

        let opts = {};
        let methodOption = {
            headers:headers
        };

        if(method == "POST"){
            opts = {
                body:JSON.stringify(data),
                cache:'no-cache',
                method:'POST',
                referrerPolicy:'no-referrer'
            };
        } else if (method == "GET") {
            opts = {
                method:'GET'
            };
        } else if (method == "PUT") {
            opts = {
                method:'PUT'
            };
        } else if (method == "DELETE") {
            opts = {
                method:'DELETE'
            };
        } else {

            // Error
        }

        methodOption = Object.assign(methodOption, opts);

        //Updateダイアログを表示
        let dialog = document.getElementById("update_dialog");
        dialog.classList.remove("update_dialog_hidden");
        await fetch(url, methodOption)
        .then(res => {
            if(res.redirected){
                location.href = location.href;
            }
            if(res.ok){
                return res.json();
            } else {
                if(res.status == 400){
                    if(typeof callbackFrom.validateDisplay == "function"){
                        let reader = res.body.getReader();
                        reader.read().then(({done, value}) => {
                            const decoder = new TextDecoder();
                            let errorBody = decoder.decode(value);
                            callbackFrom.validateDisplay(errorBody);
                        });
                    }
                    return;
                } else if(res.status == 419){
                    location.href = location.href;
                }
                // 400はValidationErrorなので処理せず
                throw new Error(res);
            }
        })
        .then(resJson => {
            if(callback != "") {
                let methods = "callbackFrom." + callback + "(resJson)";
                return eval(methods);
            }
            return resJson;
        })
        .catch(res => {
            return;
        })
        .finally(()=>{
            //Updateダイアログを非表示
            dialog.classList.add("update_dialog_hidden");
        })
    }

    /**
     * POST
     * @param {string} url
     * @param {Object} headers
     * @param {object} data リクエストボディのデータ
     * @throws {error}
     * @returns {Promise}
     */
    static async post(url, headers, data){
        let dialog = document.getElementById("update_dialog");
        try {
            //Updateダイアログを表示
            dialog.classList.remove("update_dialog_hidden");
            let response = await fetch(
                url,
                {
                    body:JSON.stringify(data),
                    cache:'no-cache',
                    headers:{...headers, 'X-Requested-With':'XMLHttpRequest'}, // request is ajax
                    method:'POST',
                    referrerPolicy:'no-referrer'
                }
            );

            if (response.status == 400){
                return response.json();
            }

            if(response.redirected){
                location.href = location.href;
            }

            if(response.status == 419){
                location.href = location.href;
            }

            if (!response.ok) {
                const json = await response.json();
                throw new Error(json.message);
            }

            return await response.json();
        } catch (error) {
            throw error;
        }finally {
            //Updateダイアログを非表示
            dialog.classList.add("update_dialog_hidden");
        }
    }
}
