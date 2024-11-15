function createNewNote() {
    let noteName = prompt("Enter the new note title:");
    if (noteName) {
        const savedNotes = document.getElementById("savedNotes");
        const newNote = document.createElement("li");
        newNote.innerHTML = `<a href="#">${noteName}</a>`;
        savedNotes.appendChild(newNote);
    }
}

function createNewGroup() {
    let groupName = prompt("Enter the new group name:");
    if (groupName) {
        const groupedNotes = document.getElementById("groupedNotes");
        const newGroup = document.createElement("li");
        newGroup.innerHTML = `<a href="#">${groupName} Notes</a>`;
        groupedNotes.appendChild(newGroup);
    }
}

function searchNotes() {
    let query = document.getElementById("searchInput").value.toLowerCase();
    let notes = document.querySelectorAll("#searchResults li a");

    notes.forEach(note => {
        if (note.textContent.toLowerCase().includes(query)) {
            note.style.display = "";
        } else {
            note.style.display = "none";
        }
    });
}
