/** 
 * Most used method in whole JS. 
 * This handles the fetching of content from different api calls with content type application/json
 * A json is expected as response from the Ajax Endpoints
 * 
 * @param {string}              url        Endpoint to which request is sent 
 * @param {array| object}       data       Optional data 
 * @param {string}              action     Http method GET|POST|PUT|PATCH. 
 * @return mixed
 */
async function fetchAPIJSON(url, data = [], action = "GET")
{
    try{
      let _action = action.toUpperCase()
      let options  = {
          method: _action,
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
          }
        }
          
      if (_action == 'POST') {
        options['body']= JSON.stringify(data)
      }
      const response = await fetch(url, options)

      if (!response.ok) {
        
        throw response.status
      }else {
        return response.json();
      }    

    }catch(err) {
      // There was an error
        switch(err){
          case 403:
            closePopups();
            popupMessage("Forbidden");
            break;
          case 500:
            closePopups();
            popupMessage("Oops, something went wrong.")
            break;
        }
        console.warn('Something went wrong.', err);
       
    };
}

/** 
 * This handles the fetching of content from different api calls using formData to optionally send files
 * 
 * @param {string}              url        Endpoint to which request is sent 
 * @param {array| object}       data       Optional data 
 * @param {string}              file       File uploaded
 * @return mixed
 */
async function fetchAPIUpload(url, data = [], file = null)
{
    try{
      
      let formData = new FormData();

      if(file){
        formData.append('file_img', file);
      }

      for( var key in data){
        if(data[key] !== null && data[key] !== undefined){
          formData.append(key, data[key]);
        }
      }

      let options  = {
        method: "POST",
        body: formData
      }

      const response = await fetch(url, options)

      if (!response.ok) {
        
        throw response.status
      }else {
        return response.json();
      }    

    }catch(err) {
      // There was an error
        switch(err){
          case 403:
            closePopups();
            popupMessage("Forbidden");
            break;
          case 500:
            closePopups();
            popupMessage("Oops, something went wrong.")
            break;
        }
        console.warn('Something went wrong.', err);
       
    };
}
