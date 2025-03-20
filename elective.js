document.addEventListener("DOMContentLoaded", function() {
    toggleMaxAnswersField();
});
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("closeModal").onclick = function() {
        document.getElementById("courseInfoModal").style.display = "none";
    };

    window.onclick = function(event) {
        let modall = document.getElementById("courseInfoModal");
        if (event.target === modall) {
            modall.style.display = "none";
            console.log('clicked outside');
        }
    };

    function getCourseInfo(courseId, infoIcon) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "get_course_info.php?courseid=" + courseId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let courseInfo = JSON.parse(xhr.responseText);
                if (courseInfo) {
                    document.getElementById("courseTitle").innerHTML = courseInfo.fullname;
                    document.getElementById("courseSummary").innerHTML = courseInfo.summary;

                    let imageDiv = document.getElementById("courseImage");
                    if (courseInfo.imageurl) {
                        imageDiv.style.backgroundImage = "url('" + courseInfo.imageurl + "')";
                        imageDiv.style.display = "block";
                    } else {
                        imageDiv.style.display = "none";
                    }

                    let modal = document.getElementById("courseInfoModal");
                    let offsetX = infoIcon.offsetLeft;
                    let offsetY = infoIcon.offsetTop;

                    modal.style.left = (offsetX + 20) + "px";
                    modal.style.top = offsetY + "px";
                    modal.style.position = "absolute";
                    modal.style.display = "block";
                    let tutorsList = document.getElementById("tutorsList");
                    tutorsList.innerHTML = '';
                    if (courseInfo.tutors && courseInfo.tutors.length > 0) {
                        courseInfo.tutors.forEach(function(tutor) {
                            let li = document.createElement('li');
                            li.textContent = tutor;
                            tutorsList.appendChild(li);
                        });
                    } else {
                        let li = document.createElement('li');
                        li.textContent = 'No tutors available';
                        tutorsList.appendChild(li);
                    }
                }
            }
        };
        xhr.send();
    }

    document.querySelectorAll(".info-icon").forEach(function(icon) {
        icon.addEventListener("click", function() {
            let courseId = this.getAttribute("data-courseid");
            getCourseInfo(courseId, this);
        });
    });
});

function showDropdown() {
    let quizIdElement = document.getElementById("quizid");

    if (!quizIdElement) {
        console.error("Element with id 'quizid' not found.");
        return;
    }

    let quizId = quizIdElement.value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "get_used_courses.php?quizid=" + quizId, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let usedCourses = JSON.parse(xhr.responseText);
            let options = document.getElementById("course-select").options;

            for (let i = 0; i < options.length; i++) {
                let option = options[i];
                if (usedCourses.includes(option.value)) {
                    option.style.display = "none";
                } else {
                    option.style.display = "";
                }
            }
        }
    };
    xhr.send();

    document.getElementById("course-select").style.display = "block";
}

function toggleMaxAnswersField() {
    let questionType = document.getElementById("questiontype").value;
    let maxAnswersGroup = document.getElementById("max-answers-group");
    let maxAnswersInput = document.getElementById("maxanswers");

    if (!maxAnswersInput) {
        console.error("Max answers input not found.");
        return;
    }

    if (questionType === "multiple") {
        maxAnswersGroup.style.display = "block";
        maxAnswersInput.style.display = "block";
        maxAnswersInput.disabled = false;
    } else {
        maxAnswersGroup.style.display = "none";
        maxAnswersInput.style.display = "none";
        maxAnswersInput.value = 1;
        maxAnswersInput.disabled = true;
    }
}

function addCourse() {
    let select = document.getElementById("course-select");
    let selectedCourses = document.getElementById("selected-courses");
    let courseidsInput = document.getElementById("courseids");
    let courseId = select.value;
    let courseName = select.options[select.selectedIndex].text;
    let addedCoursesText = document.getElementById("added-courses-text");

    let currentIds = courseidsInput.value ? courseidsInput.value.split(",") : [];

    if (!currentIds.includes(courseId)) {
        let courseDiv = document.createElement("div");
        courseDiv.style.display = "flex";
        courseDiv.style.alignItems = "center";
        courseDiv.style.marginTop = "5px";

        let courseNameDiv = document.createElement("div");
        courseNameDiv.textContent = courseName;
        courseNameDiv.style.flexGrow = "1";
        courseNameDiv.style.color = "#000080";
        courseDiv.appendChild(courseNameDiv);

        let removeBtn = document.createElement("button");
        removeBtn.textContent = removeBtnText;

        removeBtn.style.marginLeft = "10px";
        removeBtn.style.backgroundColor = "#000080";
        removeBtn.style.color = "white";
        removeBtn.style.border = "none";
        removeBtn.style.borderRadius = "4px";
        removeBtn.onclick = function() {
            removeCourse(courseId, courseDiv);
        };
        courseDiv.appendChild(removeBtn);

        selectedCourses.appendChild(courseDiv);

        currentIds.push(courseId);
        courseidsInput.value = currentIds.join(",");
        addedCoursesText.style.display = "block";
    }

    select.style.display = "none";
    document.getElementById("course-search").value = "";
    document.getElementById("course-search").blur();
    resetDropdown();
}

function removeCourse(courseId, courseDiv) {
    let courseidsInput = document.getElementById("courseids");
    let currentIds = courseidsInput.value.split(",");
    let index = currentIds.indexOf(courseId);

    if (index !== -1) {
        currentIds.splice(index, 1);
        courseidsInput.value = currentIds.join(",");
    }
    courseDiv.remove();

    if (currentIds.length === 0) {
        document.getElementById("added-courses-text").style.display = "none";
    }

    resetDropdown();
}

function filterCourses() {
    let filter = document.getElementById("course-search").value.toLowerCase();
    let options = document.getElementById("course-select").options;
    let usedCourses = document.getElementById("courseids").value.split(",");

    for (let i = 0; i < options.length; i++) {
        let option = options[i];
        let courseId = option.value;

        if (option.style.display !== "none" && option.text.toLowerCase().includes(filter) && !usedCourses.includes(courseId)) {
            option.style.display = "";
        } else {
            option.style.display = "none";
        }
    }
}

function resetDropdown() {
    let options = document.getElementById("course-select").options;
    for (let i = 0; i < options.length; i++) {
        options[i].style.display = "";
    }
}

function deleteQuestion(questionId) {
    if (confirm(removeConfirmation)) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_question.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    let questionDiv = document.getElementById("question-" + questionId);
                    if (questionDiv) {
                        questionDiv.remove();
                    }
                    location.reload();
                } else {
                    alert("Error deleting question: " + response.message);
                }
            }
        };
        xhr.send("questionid=" + questionId);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    let multipleChoiceQuestions = document.querySelectorAll(".multiple-choice");

    multipleChoiceQuestions.forEach(function(question) {
        question.addEventListener("change", function() {
            let questionId = this.name.split('_')[1].replace('[]', '');
            let maxAnswers = document.querySelector("#maxanswers_" + questionId).value;
            let selectedOptions = document.querySelectorAll("input[name='question_" + questionId + "[]']:checked");

            if (selectedOptions.length > maxAnswers) {
                alert(alertMultipleOptionsExceeded + maxAnswers + alertMultipleOptionsExceeded2);
                this.checked = false;
            }
        });
    });
});

function validateForm() {
    let questions = document.querySelectorAll(".question-container");
    let isValid = true;

    document.querySelectorAll('.validation-message').forEach(function(el) {
        el.remove();
    });

    for (let i = 0; i < questions.length; i++) {
        let question = questions[i];
        let checkboxes = question.querySelectorAll(".multiple-choice");
        let radioButtons = question.querySelectorAll("input[type='radio']");

        if (radioButtons.length > 0) {
            let isChecked = false;
            for (let k = 0; k < radioButtons.length; k++) {
                if (radioButtons[k].checked) {
                    isChecked = true;
                    break;
                }
            }
            if (!isChecked) {
                let validationMessage = document.createElement('p');
                validationMessage.className = 'validation-message';
                validationMessage.style.color = 'red';
                validationMessage.textContent = "Please select an option for this question.";
                question.appendChild(validationMessage);
                isValid = false;
            }
        }

        if (checkboxes.length > 0) {
            let questionId = question.querySelector(".multiple-choice").name.split('_')[1].replace('[]', '');
            let maxAnswersElement = document.getElementById("maxanswers_" + questionId);
            if (!maxAnswersElement) {
 continue;
}
            let maxAnswers = parseInt(maxAnswersElement.value);

            let checkedCount = 0;
            for (let j = 0; j < checkboxes.length; j++) {
                if (checkboxes[j].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount !== maxAnswers) {
                let validationMessage = document.createElement('p');
                validationMessage.className = 'validation-message';
                validationMessage.style.color = 'red';
                validationMessage.textContent = "You must select exactly " + maxAnswers + " answers for this question.";
                question.appendChild(validationMessage);
                isValid = false;
            }
        }
    }
    return isValid;
}
function clearCourses() {
    let courseidsInput = document.getElementById("courseids");
    courseidsInput.value = "";

    let selectedCourses = document.getElementById("selected-courses");
    selectedCourses.innerHTML = "";

    let addedCoursesText = document.getElementById("added-courses-text");
    addedCoursesText.style.display = "none";

    resetDropdown();

    document.getElementById("course-search").value = "";

    document.getElementById("course-select").style.display = "none";
}

function deleteStudentElectives(userid, instanceid) {
    if (confirm(deleteStudentElectivesNotification)) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", 'delete_elective.php', true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert("Error deleting electives: " + response.message);
                }
            }
        };

        xhr.send("userid=" + userid + "&instanceid=" + instanceid + "&sesskey=" + M.cfg.sesskey);
    }
}
