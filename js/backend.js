class MKWBackend
{
    request(route, data, responseFunction) {
        fetch("./backend/router.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({"route": route, "data": data})
        })
        .then(response => response.json())
        .then((data) => {
            console.log("Response from backend:");
            console.log(data);
            responseFunction(data);
        })
        .catch(error => console.error("Error: ", error));
    }
}

export const backend = new MKWBackend();
