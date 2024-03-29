# NOTE: The repo is not intented for public yet. I will update it once Rihal winners anounce (9 May 23).  

## I made video to showcase the routes please check it 
[Rihal Backend Code Challenge - PDFs Restful API Solution](https://youtu.be/12oXALh34gE)


# Project Description: 
- Laravel 9 
- Mysql Database
- Apache Server
- PHP 8.1.9
- Firebase Storage

You can use any apache or nginx, I use laragon to get Apache and Mysql 
# Installing Laravel with Laragon

1. Download and install Laragon from the official website: https://laragon.org/download/
2. In Laragon click Root and extract the project file there
![image](https://user-images.githubusercontent.com/86527969/233466361-fb243415-1dbd-48e5-819e-220f62969728.png)
3. Laragon should regonize the project once you click START ALL.
4. Now you can navigate to `http://rihal_restful_api.test/` in the browser and see the laravel welcome page.

# Checking the routes using postman 
## Downloading Postman

1. Go to the Postman website at https://www.postman.com/downloads/.
2. Select your operating system from the list of options.
3. Click on the "Download" button.
4. Once the download is complete, run the installer and follow the instructions to install Postman on your computer.

## Using Postman

1. Open Postman.
2. You will see the Postman interface, which is divided into different sections.
3. To create a new request, click on the "New" button on the top left corner of the window.
4. Select the HTTP method of your choice (GET, POST, PUT, DELETE, etc.).
5. Enter the request URL in the address bar.
6. If the request requires any headers or parameters, enter them in the appropriate section.
7. Click on the "Send" button to send the request.
8. View the response in the "Response" section of the window.


## Checking the Project
- Run `php artisan migrate` in terminal to migrate the database tables. 
- You can run test using `php test start` to check that user functionality is working correctly.

## Create User using Postman 
Route : POST `http://rihal_restful_api.test/api/users`
Body : fillable 
            - name
            - email
            - password
            - role (type admin)
![image](https://user-images.githubusercontent.com/86527969/233470265-94f6af6f-22e5-431f-9450-2de65b0390c8.png)


Once you have the user created you can use it for Basic auth
![image](https://user-images.githubusercontent.com/86527969/233470737-a554fb95-387a-4d8b-9a86-96d698916325.png)


## API Routes

### PDFs
Note: Those routes are protected using Basic Auth

- Method: GET|HEAD
    - Route: api/pdfs
    - Description: Returns a list of all PDF files.

- Method: GET|HEAD
    - Route: api/pdfs/search
    - Description: Searches for PDF files based on query parameters.
    - Parameter: 'keyword' 

- Method: POST
    - Route: api/pdfs/upload
    - Description: Uploads a PDF file.
    - Body: key: file , value: the pdf file you want to upload.

- Method: DELETE
    - Route: api/pdfs/{id}
    - Description: Deletes a specific PDF file, where `{id}` is the ID of the PDF file.

- Method: GET|HEAD
    - Route: api/pdfs/{id}/download
    - Description: Downloads a specific PDF file, where `{id}` is the ID of the PDF file.

- Method: GET|HEAD
    - Route: api/pdfs/{id}/lookup
    - Description: Searches for a specific word in a specific PDF file, where `{id}` is the ID of the PDF file.
    - Params: 'word'
    - Example `http://rihal_restful_api.test/api/pdfs/1/lookup?word=banana`
    
- Method: GET|HEAD
    - Route: api/pdfs/{id}/sentences
    - Description: Returns all sentences in a specific PDF file, where `{id}` is the ID of the PDF file.

- Method: GET|HEAD
    - Route: api/pdfs/{id}/top-words
    - Description: Returns the top 5 most frequently occurring words in a specific PDF file, where `{id}` is the ID of the PDF file.




### Users

- Method: GET|HEAD
    - Route: api/users
    - Description: Returns a list of all users.


- Method: POST
    - Route: api/users
    - Description: Creates a new user.


- Method: GET|HEAD
    - Route: api/users/{user}
    - Description: Returns the user with the specified ID.


- Method: PUT|PATCH
    - Route: api/users/{user}
    - Description: Updates the user with the specified ID.


- Method: DELETE
    - Route: api/users/{user}
    - Description: Deletes the user with the specified ID.
