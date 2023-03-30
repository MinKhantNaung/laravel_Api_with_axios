<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Api Data CRUD</title>
    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h5>Posts</h5>
                <span id="successMsgForUpdate"></span>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TITLE</th>
                            <th>DESCRIPTION</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>

            </div>
            <div class="col-md-4">
                <h5>Add Posts</h5>
                <span id="successMsg"></span>

                <form name="myForm">
                    <div class="mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control">
                        <span id="titleError"></span>
                    </div>
                    <div class="mb-3">
                        <label for="desc">Description</label>
                        <textarea name="description" id="desc" class="form-control"></textarea>
                        <span id="descError"></span>
                    </div>
                    <div class="mb-3">
                        <button type="submit" name="button" class="btn btn-primary col-12">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Edit Post</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form name="editForm" id="updateModal">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                            <span id="titleError"></span>
                        </div>
                        <div class="mb-3">
                            <label for="desc">Description</label>
                            <textarea name="description" id="desc" class="form-control" required></textarea>
                            <span id="descError"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
{{-- Jquery CDN --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
{{-- Bootstrap CDN --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous">
</script>
{{-- AXIOS --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    let tableBody = document.querySelector('#tableBody');
    let idList = document.getElementsByClassName('idList');
    let titleList = document.getElementsByClassName('titleList');
    let descList = document.getElementsByClassName('descList');
    let btnList = document.getElementsByClassName('btnList');

    // ============ Read ============
    axios.get('/api/posts')
        .then(response => {
            console.log(response);
            response.data.posts.forEach(item => {
                displayData(item);
            });
        })
        .catch(error => {
            console.log(error);
            if (error.response.status == 404) {
                console.log('"' + error.response.config.url + '" url is not found!');
            }
        });

    // =========== Create ==============
    const myForm = document.forms['myForm'];

    myForm.onsubmit = function(e) {
        e.preventDefault();
        const title = myForm['title'].value; // first step for create
        const description = myForm['description'].value; // first step for create
        let titleError = document.querySelector('#titleError'); // for show title error // 2 step
        let descError = document.querySelector('#descError'); // for show desc error // 2 step

        axios.post('/api/posts', {
            title: title,
            description: description,
        }).then(response => {
            console.log(response.data.msg);
            console.log(response.data);

            // for create success
            if (response.data.msg == 'Data Created Successfully!') {
                // for show success Error
               // for success alert
                alertMsg(response.data.msg);

                titleError.innerHTML = descError.innerHTML = '';
                myForm.reset();
                displayData(response.data.post);
            } else {
                titleError.innerHTML = title == '' ?
                    `<i class="text-danger">${response.data.msg.title}</i>` : '';
                descError.innerHTML = description == '' ?
                    `<i class="text-danger">${response.data.msg.description}</i>` : '';
            }
        }).catch(error => {
            console.log(error.response);
        });
    }

    // =========== Edit And Update =================
    const editForm = document.forms['editForm'];
    let title = editForm['title'];
    let description = editForm['description'];
    let postIdToUpdate;
    // for add data after update
    let oldTitle = '';
    // Edit
    function editBtn(postId) {
        postIdToUpdate = postId;

        axios.get('/api/posts/' + postId)
            .then(response => {
                // console.log(response.data.title, response.data.description);
                title.value = oldTitle = response.data.post.title;
                description.value = response.data.post.description;
            })
            .catch(error => {
                console.log(error);
            });
    }

    // ============= Update ==================
    editForm.onsubmit = function (event) {
        event.preventDefault();

        axios.put('/api/posts/' + postIdToUpdate, {
            title: title.value,
            description: description.value,
        })
            .then(response => {
                console.log(response.data.msg);
                // for success alert
                alertMsg(response.data.msg);
                // for change data
                for (let i = 0; i < titleList.length; i++) {
                    if (titleList[i].innerHTML == oldTitle) {
                        titleList[i].innerHTML = title.value;
                        descList[i].innerHTML = description.value;
                    }
                }
                // for hiding modal
                $('#editModal').modal('hide');
            })
            .catch(error => {
                console.log(error);
            });
    }

    // ============ Delete ==================
    function deleteBtn(deleteId) {

        if (confirm('Sure to delete?')) {
            axios.delete('/api/posts/' + deleteId)
            .then(res => {
            // for success alert
            oldTitle = res.data.old.title;
            alertMsg(res.data.msg);
                console.log(res.data.msg);
                for (let i = 0; i < titleList.length; i++) {
                    if (titleList[i].innerHTML == oldTitle) {
                        idList[i].style.display=titleList[i].style.display=descList[i].style.display=btnList[i].style.display= 'none';
                    }
                }

            })
            .catch(error => {
                console.log(error);
            });
        }
    }

    // ========== Helper Function ================
    function displayData(data) {
        tableBody.innerHTML += `
                    <tr>
                        <td class="idList">${data.id}</td>
                        <td class="titleList">${data.title}</td>
                        <td class="descList">${data.description}</td>
                        <td class="btnList">
                            <button type="button" class="btn btn-success btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editBtn(${data.id})">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm mt-1" onclick="deleteBtn(${data.id})">Delete</button>
                        </td>
                    </tr>
                `;
    }

    // =========== Success Msg =================
    function alertMsg(msg) {
        document.querySelector('#successMsgForUpdate').innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>${msg}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            `;
    }
</script>

</html>
