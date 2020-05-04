
/*
* Create schema for quiz
* */
db.createCollection("quiz",{
    validator: {
        $jsonSchema:{
            bsonType: "object",
            required:["q_name","q_content","q_difficult"],
            properties:{
                q_name:{
                    bsonType: "string",
                    description: "STRING, REQUIRED"
                },
                q_content:{
                    bsonType: "string",
                    description: "STRING, REQUIRED"
                },

                q_difficult: {
                    bsonType: "int",
                    minimum: 0,
                    maximum: 9,
                    description: "INT,  0 <= m < 10 "
                }
            }
        }
    }
})


/*
* Create schema for questions */
db.createCollection('question',{
    validator:{
        $jsonSchema:
            {
                bsonType: 'object',
                required: ["q_id","qst_name","qst_content","qst_difficult","qst_answer","qst_correctAnswer"],
                properties:{
                    q_id:{  bsonType: "objectId" },
                    qst_name:{  bsonType: "string" },
                    qst_content: {bsonType: "string" },
                    qst_difficult:{  bsonType:"int" },
                    qst_answer :{bsonType: "array"},
                    qst_correctAnswer : {enum:['a','b','c','d','t','f']}
                }
            }
    })
/*
* Create schema for answers*/
db.createCollection("answer",{
    validator:{
        $jsonSchema:{
            bsonType: 'object',
            required: ["q_id","answers"],
            properties:{
                q_id:{bsonType:"objectId"},
                answers: {bsonType:"array"}/* object {qst_id, answer}*/
            }
        }
    }
});